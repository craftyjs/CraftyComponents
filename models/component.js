var mongoose = require('mongoose');
var validate = require('mongoose-validator').validate;
var semver = require('semver');
var Mixed = mongoose.Schema.Types.Mixed;

var urlValidator = validate({ passIfEmpty: true }, 'isUrl');

var versionValidator = function(version) {
  return semver.valid(version);
};

var personValidator = function(person) {
  if (typeof person == 'object') {
    // TODO: check for a valid person object
    return true;
  } else if (typeof person == 'string') {
    // TODO: check for a match with "Barney Rubble <b@rubble.com> (http://barnyrubble.tumblr.com/)"
    return true;
  }

  return false;
};

var licenseValidator = function(license) {
  if (typeof license == 'object') {
    if (license.length > 0) {
      // TODO: check for an array of valid license objects
      return true;
    } else {
      return false;
    }
  } else if (typeof license == 'string') {
    // TODO: check for presence in list of SPDX license IDs
    return true;
  }

  return false;
};

var repositoryValidator = function(repository) {
  if (typeof repository == 'object') {
    // TODO: check for a valid repository object
    return true;
  } else if (typeof repository == 'string') {
    // TODO: check for a valid url string
    return true;
  }

  return false;
};

var componentSchema = mongoose.Schema({
  name: { type: String, required: true },
  version: { type: String, required: true, validate: versionValidator },
  description: String,
  keywords: Array,
  homepage: { type: String, validate: urlValidator },
  author: { type: Mixed, validate: personValidator },
  license: { type: Mixed, validate: licenseValidator },
  repository: { type: Mixed, validate: repositoryValidator }
});

componentSchema.index({ name: 1, description: 1, keywords: 1 });

var Component = mongoose.model('Component', componentSchema);

module.exports = Component;