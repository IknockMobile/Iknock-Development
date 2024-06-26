import axios from 'axios';
import {getUserToken} from '../UserPreference';

class HttpServiceManager {
  static myInstance = null;
  static axiosInstance = null;

  static getInstance() {
    if (HttpServiceManager.myInstance == null) {
      HttpServiceManager.myInstance = new HttpServiceManager();
    }
    return this.myInstance;
  }

  static initialize = (baseURL, authHeader) => {
    HttpServiceManager.getInstance().axiosInstance = axios.create({
      baseURL: baseURL,
      timeout: 60000,
      headers: authHeader,
    });
    // HttpServiceManager.getInstance().axiosInstance.defaults.headers.common['Content-Type'] = 'multipart/form-data';

    HttpServiceManager.getInstance().axiosInstance.interceptors.request.use(
      function (config) {
        return new Promise((resolve, reject) => {
          getUserToken().then(value => {
            if (value !== undefined && value !== null) {
              // config.data = {}
              config.headers['user-token'] = value;
              resolve(config);
              console.log('CONFIG:1 ', config);
            } else {
              config.headers['user-token'] = '';
              resolve(config);
              console.log('CONFIG:2 ', config);
            }
          });
        });
      },
      function (error) {
        return Promise.reject(error);
      },
    );
  };

  request = (requestName, parameters, method) => {
    const data = method === 'get' ? undefined : parameters;
    if (HttpServiceManager.getInstance().axiosInstance !== null) {
      return new Promise((resolve, reject) => {
        let reqParam = {
          method: method,
          url: requestName,
          data: data,
          params: parameters,
        };
        HttpServiceManager.getInstance()
          .axiosInstance.request(reqParam)
          .then(response => {
            console.log('response', response.data);
            if (response.data.code === 200 || response.data.code === 204) {
              resolve(response.data);
            } else {
              reject('Unknown error');
            }
            // showLoader(false);
          })
          .catch(error => {
            console.log('error: ', error);
            reject(HttpServiceManager.checkError(error));
            //showLoader(false);
          });
      });
    } else {
      console.warn(
        'HttpServiceManager method "initialize" is not called, call it in App.js componentDidMount',
      );
    }
  };

  static checkError = error => {
    if (error.response === undefined) {
      return error.message;
    } else if (error.response.status === 500) {
      return 'Html cannot be parsed';
    } else if (error.response.status === 503) {
      return error.message;
    } else if (error.response.status === 403) {
      return error.message;
    } else if (error.response.status === 404) {
      // console.log(Object.keys(error.response.data.data[0]))

      var values = Object.keys(error.response.data.data[0]).map(key => {
        return error.response.data.data[0][key];
      });
      // console.log('\n• ' + values.join('{"\n•"}'))
      return values.join('\n• ');
    } else {
      return error.message;
    }
  };
}
export default HttpServiceManager;
