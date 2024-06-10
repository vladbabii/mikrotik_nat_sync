FROM php:8.2-cli-alpine3.20
RUN mkdir /app
RUN mkdir /app/files
WORKDIR /app
COPY *.php .
CMD ["php", "index.php"]
