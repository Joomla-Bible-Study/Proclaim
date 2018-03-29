spawn = require('child_process').spawn
path  = require 'path'

JAVA_PATH = exports.JAVA_PATH = 'java'
JAR_PATH  = exports.JAR_PATH  = path.join __dirname, 'vendor/yuicompressor.jar'
OPTIONS   = exports.OPTIONS   =
  type: 'js'

exports.compile = (input, options, callback) ->
  if callback
    result = {}
    Object.keys(OPTIONS).forEach (key) ->
      result[key] = OPTIONS[key]
    Object.keys(options).forEach (key) ->
      result[key] = options[key]
    options = result
  else
    callback = options
    options  = OPTIONS

  args = ['-jar', JAR_PATH]

  Object.keys(options).forEach (key) ->
    return if options[key] is false
    args.push "--#{key}"
    args.push options[key].toString() unless options[key] is true

  compiler = spawn JAVA_PATH, args
  result   = ''
  errors   = ''

  compiler.stdout.setEncoding 'utf8'

  compiler.stdout.on 'data', (data) ->
    result += data
  
  # Buffer STDERR into errors
  compiler.stderr.on 'data', (data) ->
    errors += data

  compiler.on 'exit', (code) ->
    # If exit code isn't 0, then return buffered STDERR
    if code isnt 0
      error      = new Error errors
      error.code = code
    else error   = null
    callback(error, result)

  compiler.stdin.write input
  compiler.stdin.end()
