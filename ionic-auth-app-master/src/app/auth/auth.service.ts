import { User } from './user.model';
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private url = 'http://localhost/DEV/ionic_login_php_mysql/php-auth-api-master/api';
  constructor(private http: HttpClient) { }

  register(user: User) {
    return this.http.post(`${this.url}/register`, user);
  }

  login(credentials: User): Observable<string> {
    return this.http.post<{ token: string }>(`${this.url}/login`, credentials).pipe(
      map(response => response.token)
    );
  }
}
