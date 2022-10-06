# github-flow-changelog

## Installation

```
git clone https://github.com/Kunstmaan/github-flow-changelog.git
cd github-flow-changelog
composer install
```

Or install it globally.

```
composer global require kunstmaan/github-flow-changelog:@dev
```

 > Make sure you have your path configured correctly.
 > See https://getcomposer.org/doc/03-cli.md#global for more information.

## Running

```
./gfc <github token> <organisation/user> <repository> > CHANGELOG.md
```

[Generate a token here](https://github.com/settings/applications)

## Example

```
./gfc changelog xxx Kunstmaan KunstmaanBundlesCMS > ~/Development/KunstmaanBundlesCMS/CHANGELOG.md
```

## Output

[Check out this changelog](https://github.com/Kunstmaan/KunstmaanBundlesCMS/blob/master/CHANGELOG.md)
