FROM leafney/ubuntu-mysql

ENV MYSQL_ROOT_PWD=test
ENV MYSQL_USER=ocean-tester
ENV MYSQL_USER_PWD=test
ENV MYSQL_USER_DB=oceanPi

WORKDIR /

RUN apt-get update && DEBIAN_FRONTENT=noninteractive apt-get install -y \
    php7.0 \
    php7.0-fpm \
    php7.0-mysql

COPY ./source /ocean
COPY ./start.sh /start.sh

EXPOSE 8080

CMD /start.sh
