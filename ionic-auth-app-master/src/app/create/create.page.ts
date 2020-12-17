import { Component } from '@angular/core';
import { HttpHeaders, HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-create',
  templateUrl: './create.page.html',
  styleUrls: ['./create.page.scss'],
})
export class CreatePage {

  constructor(private http: HttpClient) { }

  onCreate() {
    // Do this on service. But for this demo lets do here
    const token = localStorage.getItem('token');
    const headers = new HttpHeaders({
      Authorization: 'Bearer ' + token
    });

    // this.http.post(`http://localhost/DEV/loginIonicPhpJwt/php-auth-api-master-postgresql/api/create`, 'body', { headers }).subscribe(console.log);
    // this.http.post(`http://localhost/DEV/loginIonicPhpJwt/php-auth-api-master-mysql/api/create`, 'body', { headers }).subscribe(console.log);

    this.http.post(`http://localhost/DEV/loginIonicPhpJwt/php-auth-api-master-mysql/api/create`, 'body', { headers }).subscribe(console.log);
  }

}