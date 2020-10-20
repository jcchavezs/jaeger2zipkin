# jaeger2zipkin

## Getting started

### Using Docker

```bash
// build the image locally
docker build -t jaeger2zipkin .

// run the image asuming you are in the folder where the file is
docker run -v $(PWD)/example_jaeger_trace.json:/usr/src/jaeger2zipkin/trace.json jaeger2zipkin trace.json http://myzipkin:9411/api/v2/spans
```

Notice, if you are running zipkin in a container locally then you should use http://host.docker.internal:9411/api/v2/spans

You can also run the command below if the trace file is accessible from a URL:

```bash
docker run jaeger2zipkin http://path.to/trace.json http://myzipkin:9411/api/v2/spans
```

### Using PHP

```bash
php main.php spans.json
```
