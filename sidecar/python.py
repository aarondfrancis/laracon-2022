import os

def handler(event, context):
    return "Hello from Python " + os.environ.get('AWS_EXECUTION_ENV')
