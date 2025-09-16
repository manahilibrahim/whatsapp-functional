// Point to your SiteGround backend (PHP)
const API = 'https://manahil.rehanacademy.net/whatsapp-functional';
document.getElementById('openApp').href = API + '/';

const j = (u,o={}) => fetch(u,{credentials:'include',
  headers:{'Content-Type':'application/json',...(o.headers||{})}, ...o
}).then(r=>r.json());

document.getElementById('login').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const identifier = document.getElementById('id').value.trim();
  const password = document.getElementById('pw').value;
  const out = document.getElementById('msg');
  out.textContent = 'Logging in...';
  try{
    const res = await j(`${API}/login.php`, {method:'POST', body:JSON.stringify({identifier,password})});
    out.textContent = res.ok ? '✅ Logged in — Open Full Chat' : (res.error || 'Login failed');
  }catch(err){ out.textContent = err.message; }
});
