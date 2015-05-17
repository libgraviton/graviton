FROM cogniteev/echo

COPY . /app

VOLUME /app

CMD ["/bin/echo", "Creating graviton app container, user php:fpm to run."]
