var should = require('should');
var request = require('supertest');
var app = require('../../app');

describe('hooks', function() {
  describe('POST /hooks/github', function() {
    it('responds with success', function(done) {
      request(app)
        .post('/hooks/github')
        .expect('Content-Type', /json/)
        .expect(200, done);
    });
  });
});