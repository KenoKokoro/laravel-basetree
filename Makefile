phpunit:
	@echo "Setting different namespace for Laravel tests"
	@php tests/Fake/Integration/Laravel/artisan app:name Laravel
	@echo "Running phpunit..."
	@vendor/bin/phpunit
	@echo "Cleanup..."
	@run rm -rf ./tests/storage
