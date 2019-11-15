phpunit:
	@echo "Running phpunit..."
	@vendor/bin/phpunit
	@echo "Cleanup..."
	@rm -rf ./tests/storage
