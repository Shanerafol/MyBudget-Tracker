// Auth is verified on the PHP server using a PHP session cookie.
let csrfToken = '';
async function apiRequest(path, options={}) {
 const response=await fetch('api/'+path,{credentials:'same-origin',cache:'no-store',...options,headers:{...(options.headers||{}),...(options.method==='POST'?{'X-CSRF-Token':csrfToken}:{})}});
 let result;try {result=await response.json()}catch {throw new Error('Invalid server response. Check Apache/PHP.');}
 if(!response.ok || (result && result.success===false)) { if(response.status===401 && !['login.php','register.php'].includes(path)) location.replace('login.html'); throw new Error(result.message || 'Request failed'); }
 return result;
}
async function initializeSession(){const result=await apiRequest('session.php');csrfToken=result.csrf;return result;}
