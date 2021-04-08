install:
	composer install
autoload:
	composer dump-autoload
validate:
	composer validate
lint:
	composer run-script phpcs -- --standard=PSR12 src bin
stan:
	composer run-script phpstan -- --level max src bin
pushAll:
	git add -A; git commit -m '$(m)'; git push