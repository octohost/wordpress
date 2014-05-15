FROM octohost/php5-apache

ADD ./default.conf /etc/apache2/sites-available/000-default.conf
ADD ./apache2.conf /etc/apache2/apache2.conf
ADD . /var/www

RUN chmod 777 /var/www/wp-content/uploads

EXPOSE 80

CMD ["/bin/bash", "/start.sh"]
