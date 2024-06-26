import DefaultPreference from 'react-native-default-preference';

export const setUser = (user) => {
  DefaultPreference.
    set('user', JSON.stringify(user)).
    then(() => {
      console.log("USER SAVED")
    });
}

export const getUserData = () => {

  return new Promise((resolve, reject) => {
    DefaultPreference.
      get('user').
      then((value) => {
        resolve(value)
      });
  });
}
export const setUserToken = (token) => {
  DefaultPreference.
    set('token', token).
    then(() => {
      console.log("TOKEN SAVED")
    });
}

export const getUserToken = () => {
  return new Promise((resolve, reject) => {
    DefaultPreference.
      get('token').
      then((value) => {
        resolve(value)
      });
  });
}

export const setLatLong = (LatLong) => {
  DefaultPreference.
    set('latlong', LatLong).
    then(() => {
      console.log("LatLong SAVED")
    });
}

export const getLatLong = () => {
  return DefaultPreference.
    get('latlong');
}

export const clearAll = () => {
  DefaultPreference.clearAll().then(() => {
    console.log('all cleared');
  }).catch((error) => {
    console.log(error);
  });
}
