# Cow

The ineptly named tool which may one day supercede the older [build tools](https://github.com/silverstripe/silverstripe-buildtools).

## Install

You can install this globally with the following commands

```
composer global require silverstripe/cow dev-master
echo 'export PATH=$PATH:~/.composer/vendor/bin/'  >> ~/.bash_profile
```

Customise your release path

```
echo 'export COW_RELEASE_PATH=~/Sites' >> ~/.bash_profile
```

Now you can run `cow` at any time, and `composer global update` to perform time-to-time upgrades.

## Commands

Cow is a collection of different tools (steps) grouped by top level commands. It is helpful to think about
not only the commands available but each of the steps each command contains.

### Setup

`cow project:create <version>` Create a new release with the given version. If you don't specify a directory `-d` it will
install to the path specified by `COW_RELEASE_PATH`/release-<version>

