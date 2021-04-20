install:
	composer install
autoload:
	composer dump-autoload
validate:
	composer validate
update:
	composer update
lint:
	composer run-script phpcs -- --standard=PSR12 src bin tests
stan:
	composer run-script phpstan
test:
	composer run-script test
test-coverage:
	composer run-script test-coverage
pushAll:
	git add -A; git commit -m '$(m)'; git push