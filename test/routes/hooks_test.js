var should = require('should');
var request = require('supertest');
var nock = require('nock');
var app = require('../../app');
var Component = require('../../models/component');

var exampleComponent = {
  name: 'test',
  version: '0.0.1',
  description: 'A Crafty component',
  keywords: ['test', 'component'],
  homepage: 'http://example.com',
  author: 'John Doe',
  license: 'MIT',
  repository: 'http://github.com/owner/project.git'
};

var exampleContent = function(content) {
  return { content: new Buffer(content).toString('base64') };
};

var examplePayload = {
  repository: { url: 'https://github.com/potomak/CraftyComponents' }
};

describe.only('hooks', function() {
  beforeEach(function(done) {
    Component.remove(function() {
      nock('https://api.github.com')
        .get('/repos/potomak/CraftyComponents/contents/component.json')
        .reply(200, exampleContent(JSON.stringify(exampleComponent)));

      done();
    });
  });

  describe('POST /hooks/github', function() {
    it('responds with success', function(done) {
      request(app)
        .post('/hooks/github')
        .send({ payload: JSON.stringify(examplePayload) })
        .expect('Content-Type', /json/)
        .expect(200, done);
    });

    it('creates a new component using component.json data', function(done) {
      request(app)
        .post('/hooks/github')
        .send({ payload: JSON.stringify(examplePayload) })
        .expect('Content-Type', /json/)
        .expect(200, function() {
          Component.find(function(err, components) {
            components.should.have.length(1);

            components[0].name.should.equal(exampleComponent.name);
            components[0].version.should.equal(exampleComponent.version);
            components[0].description.should.equal(exampleComponent.description);
            components[0].keywords.should.be.instanceof(Array).and.have.lengthOf(2).and.include('test').and.include('component');
            components[0].homepage.should.equal(exampleComponent.homepage);
            components[0].author.should.equal(exampleComponent.author);
            components[0].license.should.equal(exampleComponent.license);
            components[0].repository.should.equal(exampleComponent.repository);

            done();
          });
        });
    });

    it('updated an existing component using component.json data', function(done) {
      var updatedComponent = exampleComponent;
      updatedComponent.version = '0.0.2';

      request(app)
        .post('/hooks/github')
        .send({ payload: JSON.stringify(examplePayload) })
        .expect('Content-Type', /json/)
        .expect(200, function() {
          nock('https://api.github.com')
            .get('/repos/potomak/CraftyComponents/contents/component.json')
            .reply(200, exampleContent(JSON.stringify(updatedComponent)));

          request(app)
            .post('/hooks/github')
            .send({ payload: JSON.stringify(examplePayload) })
            .expect('Content-Type', /json/)
            .expect(200, function() {
              Component.find(function(err, components) {
                components.should.have.length(1);

                components[0].name.should.equal(exampleComponent.name);
                components[0].version.should.equal(updatedComponent.version);
                components[0].description.should.equal(exampleComponent.description);
                components[0].keywords.should.be.instanceof(Array).and.have.lengthOf(2).and.include('test').and.include('component');
                components[0].homepage.should.equal(exampleComponent.homepage);
                components[0].author.should.equal(exampleComponent.author);
                components[0].license.should.equal(exampleComponent.license);
                components[0].repository.should.equal(exampleComponent.repository);

                done();
              });
            });
        });
    });
  });
});