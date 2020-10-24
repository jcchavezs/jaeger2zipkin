<?php

require_once './vendor/autoload.php';

use Zipkin\Reporters\Http;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if (count($argv) < 2) {
    echo "Usage:\n\n";
    printf("    php %s <jaeger trace path> [<zipkin endpoint>]\n\n", $argv[0]);
    echo "By default zipkin endpoint is \"http://localhost:9411/api/v2/spans\"\n";
    exit(1);
}

$jaegerTracePath = $argv[1];
$content = file_get_contents($jaegerTracePath);
if ($content === false) {
    printf("Failed to read \"%s\".", $jaegerTracePath);
    exit(1);
}

if ($content === '') {
    printf("File \"%s\" is empty.", $jaegerTracePath);
    exit(1);
}

$jaegerTrace = json_decode($content, true);
if ($jaegerTrace === false || $jaegerTrace === null) {
    printf("Failed to decode \"%s\".", $jaegerTracePath);
    exit(1);
}

if (!array_key_exists('data', $jaegerTrace)) {
    printf("Malformed jaeger trace, \"data\" value not found.");
    exit(1);
}

$processesMap = $jaegerTrace['data'][0]['processes'];

$spans = array_map(function (array $jaegerSpan) use ($processesMap) {
    $serviceName = $processesMap[$jaegerSpan['processID']]['serviceName'];
    $defaultTags = $processesMap[$jaegerSpan['processID']]['tags'];
    return jaegerToZipkinSpan($jaegerSpan, $serviceName, $defaultTags);
}, $jaegerTrace['data'][0]['spans']);

// initialize logger
$logger = new Logger('name');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));

// report traces to zipkin collector
$reporter = new Http(['endpoint_url' => $argv[2] ?? 'http://localhost:9411/api/v2/spans'], null, $logger);
$reporter->report($spans);
