var URL = require('url');
var githubContents = require('github-contents');

// POST /hooks/github
exports.github = function(req, res, next) {
  var payload = JSON.parse(req.body.payload);
  var repoUrl = URL.parse(payload.repository.url);

  githubContents(repoUrl.path.substring(1), 'asd.json', function(err, contents) {
    if (err) {
      return next(err);
    }

    console.log(contents);
    res.json(200);
  });
};