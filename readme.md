# Cow

The ineptly named tool which may one day supercede the older [build tools](https://github.com/silverstripe/silverstripe-buildtools).

## Install

You can install this globally with the following commands

```
composer global require silverstripe/cow dev-master
echo 'export PATH=$PATH:~/.composer/vendor/bin/'  >> ~/.bash_profile
```

Now you can run `cow` at any time, and `composer global update` to perform time-to-time upgrades.

If you're feeling lonely, or just want to test your install, you can run `cow moo`.

## Commands

Cow is a collection of different tools (steps) grouped by top level commands. It is helpful to think about
not only the commands available but each of the steps each command contains.

It is normally recommended that you run with `-vvv` verbose flag so that errors can be viewed during release.

### Setup

`cow project:create <version> -vvv` Create a new release with the given version. If you don't specify a directory `-d` it will
install to the path specified by `./release-<version>` in the current directory.

### Changelog

`cow release:changelog <version> --from <fromversion> -vvv` Generates a changelog for version <version>, where
<fromversion> is the starting point of the log history.
