# jaeger2zipkin

This library turns a jaeger trace (downloaded from the UI) into a zipkin trace that can be reingested through a zipkin endpoint.

Jaeger cannot (at least I could not find a way) to reingest a downloaded trace because the format is different and also requires send them over thrift. Fortunately, jaeger (like other zipkin's forks) expose an endpoint ingest zipkin data.

## Getting started

### Using Docker

```bash
// build the image locally
docker build -t jaeger2zipkin .

// run the image asuming you are in the folder where the file is
docker run -v $(PWD)/example_jaeger_trace.json:/usr/src/jaeger2zipkin/trace.json jaeger2zipkin trace.json http://myzipkin:9411/api/v2/spans
```

Notice, if you are running zipkin in a container locally (e.g. `docker run -p 9411:9411 -d openzipkin/zipkin`) then you should use the endpoint http://host.docker.internal:9411/api/v2/spans.

You can also run the command below if the trace file is accessible from a URL:

```bash
docker run jaeger2zipkin http://path.to/trace.json http://myzipkin:9411/api/v2/spans
```

### Using PHP

```bash
php Main.php spans.json
```

## FAQ

### Why PHP?

Because I like PHP but also because tags format in jaeger breaks typing, making the unmarshaling cumbersome:

```json
{
    "key": "rpc.request.metadata.content-type",
    "type": "string",
    "value": "application/grpc"
},
{
    "key": "FailFast",
    "type": "bool",
    "value": false
},
{
    "key": "status.code",
    "type": "int64",
    "value": 0
},
```
