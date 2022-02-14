module.exports = [
      {
        path: '/graphql',
        rule: { target: 'http://127.0.0.1:8000' }
      },
      {
        path: '/sanctum/csrf-cookie',
        rule: { target: 'http://127.0.0.1:8000' }
      }
    ]
