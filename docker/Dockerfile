FROM lorisleiva/laravel-docker:latest

RUN apk update
RUN apk add redis
RUN apk add tmux
RUN apk add vim
RUN apk add supervisor
RUN rm -rf /tmp/* /var/cache/apk/*
ADD docker/supervisord.conf /etc/
ADD docker/credentials /root/.aws/
ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
