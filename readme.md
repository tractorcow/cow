# Cow

The ineptly named tool which may one day supercede the older [build tools](https://github.com/silverstripe/silverstripe-buildtools).

## Install

You can install this globally with the following commands

```
composer global require silverstripe/cow dev-master
echo 'export PATH=$PATH:~/.composer/vendor/bin/'  >> ~/.bash_profile
```

Now you can run `cow` at any time, and `composer global update` to perform time-to-time upgrades.

Make sure that you setup your AWS credentials properly, and create a separate profile named `silverstripe`
for this. You'll also need the aws cli installed.

If you're feeling lonely, or just want to test your install, you can run `cow moo`.

## Commands

Cow is a collection of different tools (steps) grouped by top level commands. It is helpful to think about
not only the commands available but each of the steps each command contains.

It is normally recommended that you run with `-vvv` verbose flag so that errors can be viewed during release.

For example, this is what I would run to release `3.1.14-rc1`, assuming there wasn't a 3.1.14 branch and I wanted
to create one for the RC release.

```
cow release 3.1.14-rc1 -vvv --from=3.1.13 --branch-auto
```

## Release

`cow release <version>` will perform all release tasks. <version> is mandatory and must be the exact tag name to release.

This command has these options:

* `-vvv` to ensure all underlying commands are echoed
* `--from <fromversion>` when generating a changelog, it can be necessary at times to specify the last released version.
  cow will try to guess, but sometimes (e.g. when releasing 3.2.0) it's not clear where the changelog should start.
* `--directory <directory>` to specify the folder to create or look for this project in. If you don't specify this,
it will install to the path specified by `./release-<version>` in the current directory.
* `--branch <branch>` or just `--branch-auto` will automatically branch each module to a temp branch for this release.
  If omitted, no branching is performed. `--branch-auto` can be used to just default to the major.minor.patch
  version of the release. It's advisable to specify this, but not always necessary, when doing pre-releases.

## Sub-commands

`release` actually has several sub-commands which can be run independently. These are as below:

* `release:create` creates the project folder
* `release:branch` Will (if needed) branch all modules
* `release:translate` Updates translations and commits this to source control
* `release:changelog` Just generates the changelog and commits this to source control.
* `release:tag` Add annotated tags to each module
* `release:push` Push branch and tag up to origin
* `release:upload` Upload archived projects to silverstripe.org

## Module-level commands

Outside of doing core releases, you can use this for specific modules

* `module:translate <modules>` Updates translations for modules and commits this to source control. If you
don't specify a list of modules then all modules will be translated. Specify 'installer' for root module.
You can use `--push` option to push to origin at the end, or `--exclude` if your list of modules is the list
to exclude. By default all modules are included, unless whitelisted or excluded.

