# jaeger2zipkin

## Getting started

### Using Docker

```bash
// build the image locally
docker build -t jaeger2zipkin .

// run the image asuming you are in the folder where the file is
docker run -v $(PWD)/spans.json:/usr/src/jaeger2zipkin/spans.json jaeger2zipkin spans.json
```

You can also run

```bash
docker run jaeger2zipkin http://path.to/spans.json
```

### Using PHP

```bash
php main.php spans.json
```
