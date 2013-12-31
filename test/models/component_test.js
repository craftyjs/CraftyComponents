var should = require('should');
var Component = require('../../models/component');

describe('Component', function() {
  it('requires `name` and `version` paths', function(done) {
    var component = new Component();
    component.validate(function(err) {
      Object.keys(err.errors).should.have.length(2);
      Object.keys(err.errors).should.include('name');
      Object.keys(err.errors).should.include('version');
      done();
    });
  });

  describe('with a valid version', function() {
    it('is valid', function(done) {
      var component = new Component({name: 'test', version: '0.0.1'});
      component.validate(function(err) {
        should.strictEqual(undefined, err);
        done();
      });
    });
  });

  describe('with an invalid version', function() {
    it('requires a valid `version`', function(done) {
      var component = new Component({name: 'test', version: 'invalid'});
      component.validate(function(err) {
        Object.keys(err.errors).should.have.length(1);
        Object.keys(err.errors).should.include('version');
        done();
      });
    });
  });
});