# oauth2-laravel-eeyes

由于不太会用league/Oauth2client包，自己写了一个。
在env文件中设置好
```angular2html
    OAUTH_CLIENT_ID
    OAUTH_CLIENT_SECRET
    OAUTH_REDIRECT_URI
```
即可使用

```php
    
    $provider = new Provider();
    $user = $provider->getResourceOwner();
    
    if(($pre_page = $user->getPrePage())){
        return redirect($pre_paget);
    }
```