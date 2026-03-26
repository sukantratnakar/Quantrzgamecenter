module.exports = {
  apps: [{
    name: 'quantraz-game',
    script: 'php',
    args: '-S 0.0.0.0:8002',
    cwd: '/Users/sukantclawdbot/clawd/quantraz-game',
    interpreter: 'none',
    env: {
      DB_HOST: 'localhost',
      DB_USER: 'root',
      DB_PASSWORD: '',
      DB_NAME: 'quantraz_game',
      DB_PORT: '3306'
    }
  }]
};
