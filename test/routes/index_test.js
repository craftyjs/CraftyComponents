var should = require('should');
var request = require('supertest');
var app = require('../../app');

describe('index', function() {
  describe('GET /', function() {
    it('responds with success', function(done) {
      request(app)
        .get('/')
        .expect(200, done);
    });
  });
});