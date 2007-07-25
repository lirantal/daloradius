
function randomPassword()
{
  length = 8;
  chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789";
  pass = "";
  for(x=0;x<length;x++)
  {
    i = Math.floor(Math.random() * 62);
    pass += chars.charAt(i);
  }
  document.newuser.password.value = pass;
}

function randomUsername()
{
  length = 8;
  chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789";
  user = "";
  for(x=0;x<length;x++)
  {
    i = Math.floor(Math.random() * 62);
    user += chars.charAt(i);
  }
  document.newuser.username.value = user;
}