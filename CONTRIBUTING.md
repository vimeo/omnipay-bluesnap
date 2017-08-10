# Contributing Guidelines

## Steps
* Fork the project.
* Make your feature addition or bug fix.
* Add tests for it. This is important so we don't break it in a future version unintentionally.
* Commit just the modifications, do not mess with the composer.json file.
* Ensure your code is nicely formatted in the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
style and that all tests pass.
* Send the pull request.
* Check that the Travis CI build passed. If not, rinse and repeat. **Note:** This repo uses [Psalm](https://github.com/vimeo/psalm) to statically analyze all the code. Psalm runs on all the builds for PHP 5.5+.

**NOTE:** This repo requires pull-request reviews for all changes on branches bound for production in accordance with Vimeo policy.

## Testing locally
To install Psalm and run the full test suite locally, download [Composer](https://getcomposer.org/) into the repository and then run:

```
make install
make
```

If you use PHP 5.3 or 5.4 locally (which are not supported by the latest version of Psalm), you can skip Psalm by running:

```
make install_no_psalm
make no_psalm
```
