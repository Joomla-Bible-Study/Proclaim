task 'build', 'Build the yui-compressor library', ->
  p = require('child_process').spawn 'coffee', ['-c', '-b', '-o', 'lib/', 'src/index.coffee']
  p.stderr.on 'data', (data) ->
    console.log data.toString()
