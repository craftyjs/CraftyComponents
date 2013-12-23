# CraftyComponents

[Crafty.js](http://craftyjs.com/) HTML5 game engine components directory.

Maintained by [Giovanni Cappellotto](http://twitter.com/johnnyaboh).

## How does it work

To get your component listed at [http://craftycomponents.com](http://craftycomponents.com) create a `component.json` at your project root following the standard described below and add a webhook in your repository to [http://craftycomponents.com/hooks/github](http://craftycomponents.com/hooks/github).

## `component.json`

This section is all you need to know about what's required in your `component.json` file. It must be actual JSON, not just a JavaScript object literal.

This section is an abridged version of the npm `package.json` specification.

### `name`

The most important things in your `component.json` are the `name` and `version` fields. They are actually required. The name and version together form an identifier that is assumed to be completely unique. Changes to the package should come along with changes to the version.

### `version`

The most important things in your `component.json` are the `name` and `version` fields. They are actually required. The name and version together form an identifier that is assumed to be completely unique. Changes to the package should come along with changes to the version.

Version must be parseable by `node-semver`, which is bundled with npm as a dependency. (`npm install semver` to use it yourself.)

### `description`

Put a description in it. It's a string. This helps people discover your component.

### `keywords`

Put keywords in it. It's an array of strings. This helps people discover your component.

### `homepage`

The url to the project homepage.

### `author`

The `author` is one *person*. A *person* is an object with a `name` field and optionally `url` and `email`, like this:

```json
"author": {
  "name": "John Doe",
  "email": "jd@example.com",
  "url": "http://example.com/"
}
```

### `license`

You should specify a license for your component so that people know how they are permitted to use it, and any restrictions you're placing on it.

The simplest way, assuming you're using a common license such as BSD-3-Clause or MIT, is to just specify the standard SPDX ID of the license you're using, like this:

```json
{ "license": "BSD-3-Clause" }
```

You can check [the full list of SPDX license IDs](https://spdx.org/licenses/). Ideally you should pick one that is [OSI](http://opensource.org/licenses/alphabetical) approved.

If you have more complex licensing terms, or you want to provide more detail in your component.json file, you can use the more verbose plural form, like this:

```json
"licenses": [
  {
    "type": "MyLicense",
    "url": "http://github.com/owner/project/path/to/license"
  }
]
```

It's also a good idea to include a license file at the top level in your package.

### `repository`

Specify the place where your code lives. This is helpful for people who want to contribute.

Example:

```json
"repository": {
  "type": "git",
  "url": "http://github.com/owner/project.git"
}
```