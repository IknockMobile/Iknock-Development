//
//  index.js:
//  BoilerPlate
//
//  Created by Retrocube on 10/4/2019, 9:49:50 AM.
//  Copyright © 2019 Retrocube. All rights reserved.
//
import {PermissionsAndroid, Platform} from 'react-native';
import _ from 'lodash';
import GeolocationService from 'react-native-geolocation-service';

class utility {
  isPlatformAndroid = () => Platform.OS === 'android';
  isPlatformIOS = () => Platform.OS === 'ios';
  // Location permission
  checkAndRequestLocation = async (onSuccess, onFailure) => {
    if (this.isPlatformAndroid()) {
      try {
        const check = await PermissionsAndroid.check(
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
        );
        if (check === false) {
          try {
            const permissionResponse = await PermissionsAndroid.request(
              PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
            );
            if (permissionResponse === PermissionsAndroid.RESULTS.GRANTED) {
              this.getCurrentLocation(onSuccess, onFailure);
            } else {
              onFailure(permissionResponse);
            }
          } catch (err) {
            onFailure(err);
          }
        } else if (check === true) {
          this.getCurrentLocation(onSuccess, onFailure);
        }
      } catch (err) {
        onFailure(err);
      }
    } else {
      this.getCurrentLocation(onSuccess, onFailure);
    }
  };

  getCurrentLocation = (onSuccess, onFailure) => {
    let options = {
      maximumAge: 1000,
      distanceFilter: 1,
      enableHighAccuracy: true,
      timeout: 20000,
    };

    if (this.isPlatformAndroid()) {
      options = {
        timeout: 20000,
        maximumAge: 1000,
        forceRequestLocation: true,
        enableHighAccuracy: true,
        distanceFilter: 1,
      };
    }
    if (Platform.OS === 'ios') {
      GeolocationService.getCurrentPosition(onSuccess, onFailure, options);
    } else {
      GeolocationService.getCurrentPosition(onSuccess, onFailure, options);
    }
  };
}

export default new utility();
