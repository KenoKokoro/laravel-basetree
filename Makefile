phpunit:
	@echo "Setting different namespace for Laravel tests"
	@php tests/Fake/Integration/Laravel/artisan app:name Laravel
	@mkdir vendor/laravel/lumen/config/
	@cp tests/Fake/database.php vendor/laravel/lumen/config/database.php
	@echo "Running phpunit..."
	@vendor/bin/phpunit
	@echo "Cleanup..."
	@rm -rf ./tests/storage
	@rm -rf vendor/laravel/lumen/config
