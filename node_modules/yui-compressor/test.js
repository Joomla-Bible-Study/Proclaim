var assert       = require('assert')
  , fs           = require('fs')
  , yui          = require('./lib')
  , INDEX_COFFEE = fs.readFileSync('./src/index.coffee', 'utf8')
  , INDEX_JS     = fs.readFileSync('./lib/index.js', 'utf8')

yui.compile(INDEX_COFFEE, function (error, result) {
  assert.ok(error)
})

yui.compile(INDEX_JS, function (error, result) {
  assert.ok(!error)
  assert.ok(result)
  assert.ok('string' === typeof result)
})
