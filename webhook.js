const http = require('http');
const { exec } = require('child_process');

const SECRET = 'mysecretkey'; // set any random secret key

http.createServer((req, res) => {
  if (req.method === 'POST' && req.url === `/deploy?secret=${SECRET}`) {
    exec('/var/www/Scout/deploy.sh', (err, stdout, stderr) => {
      if (err) console.error(err);
      console.log(stdout);
    });
    res.end('Deployment triggered');
  } else {
    res.end('Invalid request');
  }
}).listen(9000, () => console.log('Webhook listening on port 9000'));
