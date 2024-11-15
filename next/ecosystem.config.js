module.exports = {
    apps: [{
      name: 'next-app',
      script: 'npm',
      args: 'start',
      env: {
        PORT: 3300,
        NODE_ENV: 'production'
      },
      watch: false,
      instances: 1,
      exec_mode: 'cluster',
      max_memory_restart: '500M'
    }]
  }