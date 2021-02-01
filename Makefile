# Test

all: with_psalm

no_psalm: style test

with_psalm: style psalm test

style:
	vendor/bin/phpcs --standard=PSR2 src && vendor/bin/phpcs --standard=PSR2 --error-severity=1 --warning-severity=6 tests

test:
	vendor/bin/phpunit

psalm:
	vendor/bin/psalm

# Install

install: install_with_psalm

install_with_psalm:
	COMPOSER=composer-psalm.json php composer.phar install

install_no_psalm:
	php composer.phar install

# Update

update: update_with_psalm

update_with_psalm:
	COMPOSER=composer-psalm.json php composer.phar update

update_no_psalm:
	php composer.phar update
