<?php

require_once './vendor/autoload.php';

use Zipkin\Reporters\JsonV2Serializer;
use Zipkin\Reporters\Http;
use Zipkin\Recording\Span;
use Zipkin\Propagation\TraceContext;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function jaeger2ZipkinSpan(array $jaegerSpan, string $serviceName, array $defaultTags): Span
{
    $parentID = null;
    if (\array_key_exists('references', $jaegerSpan) && count($jaegerSpan['references']) > 0) {
        $parentID = $jaegerSpan['references'][0]['spanID'];
    }

    $context = TraceContext::create(
        $jaegerSpan['traceID'],
        $jaegerSpan['spanID'],
        $parentID,
    );

    $endpoint = Zipkin\Endpoint::create($serviceName);

    $span = Span::createFromContext($context, $endpoint);
    $span->setName($jaegerSpan['operationName'] ?: '');
    $span->start($jaegerSpan['startTime'] ?: 0);
    $span->tag('internal.span.format', 'jaeger');
    foreach ($jaegerSpan['tags'] + $defaultTags as $tag) {
        if ($tag['key'] === 'rpc.request.metadata.grpc-trace-bin') {
            // this is binary data and might break the json, skipping for now
            continue;
        }

        if ($tag['key'] === 'span.kind') {
            $span->setKind(strtoupper($tag['value']));
            continue;
        }

        $span->tag($tag['key'], (string) $tag['value']);
    }
    $span->finish($jaegerSpan['startTime'] + $jaegerSpan['duration']);
    return $span;
}

if (count($argv) < 2) {
    echo "Usage:\n\n";
    printf("    php %s <jaeger trace path> [<zipkin endpoint>]\n\n", $argv[0]);
    echo "By default zipkin endpoint is \"http://localhost:9411/api/v2/spans\"\n";
    exit(1);
}

$jaegerTracePath = $argv[1];
$content = file_get_contents($jaegerTracePath);
if ($content === false) {
    printf("Failed to read \"%s\"", $jaegerTracePath);
    exit(1);
}

$jaegerTrace = json_decode($content, true);

$processesMap = $jaegerTrace['data'][0]['processes'];

$spans = array_map(function (array $jaegerSpan) use ($processesMap) {
    $serviceName = $processesMap[$jaegerSpan['processID']]['serviceName'];
    $defaultTags = $processesMap[$jaegerSpan['processID']]['tags'];
    return jaeger2ZipkinSpan($jaegerSpan, $serviceName, $defaultTags);
}, $jaegerTrace['data'][0]['spans']);

$logger = new Logger('name');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));

//file_put_contents("zipkin_spans.json", (new JsonV2Serializer)->serialize($spans));

$reporter = new Http(['endpoint_url' => $argv[2] ?? 'http://localhost:9411/api/v2/spans'], null, $logger);
$reporter->report($spans);

echo "Spans reported successfully\n";
