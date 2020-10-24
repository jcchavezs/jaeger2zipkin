<?php

require_once './vendor/autoload.php';

use Zipkin\Recording\Span;
use Zipkin\Propagation\TraceContext;

function jaegerToZipkinSpan(array $jaegerSpan, string $serviceName, array $defaultTags): Span
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
