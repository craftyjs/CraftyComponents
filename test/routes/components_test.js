var should = require('should');
var request = require('supertest');
var app = require('../../app');

describe('components', function() {
  describe('GET /components', function() {
    it('responds with success', function(done) {
      request(app)
        .get('/components')
        .expect(200, done);
    });
  });
});