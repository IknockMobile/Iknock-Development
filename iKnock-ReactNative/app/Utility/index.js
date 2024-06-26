import { Linking, Platform, Dimensions, Alert, } from 'react-native';
import _ from "lodash";

export const getDeviceWidth = () => {
    return Dimensions.get('window').width;
}
export const getDeviceHeight = () => {
    return Dimensions.get('window').height;
}
export const getCurrDate = () => {
    var date, day, month, year;
    date = new Date();
    day = date.getDate();
    month = date.getMonth() + 1;
    year = date.getFullYear();
    return new Date();
}
export const isImageNull = imageUrl =>
    _.isEmpty(imageUrl)
        ? "https://reactnativecode.com/wp-content/uploads/2018/02/Default_Image_Thumbnail.png"
        : imageUrl;

export const isNull = (value) => {
    return _.isNull(value);
}

export const isEmpty = (value) => {
    return _.isEmpty(value);
}

export const openExternalApp = (url) => {
    Linking.canOpenURL(url).then(supported => {
        if (!supported) {
            console.log('Can\'t handle url: ' + url);
        } else {
            return Linking.openURL(url);
        }
    }).catch(err => console.error('An error occurred', err));
}
export const showMessage = (
    title: String = "",
    message: String,
    callback
) => {

    Alert.alert(
        title === "" ? "Alert" : title,
        message,
        [{ text: "OK", onPress: () => (callback ? callback() : callback) }],
        { cancelable: true }
    );
};


export const dateFormate = (date) => {
    dateSplit = date.split('-')
    return dateSplit[2] + "-" + dateSplit[0] + "-" + dateSplit[1];
}
export const splitStr = (value, operator) => {
    return value.split(operator);
}

export const mapUrl = (saddr, daddr) => {

    return Platform.select({
        ios: 'maps://app?saddr=' + saddr + '&daddr=' + daddr,
        android: 'https://maps.google.com/maps?saddr=' + saddr + '&daddr=' + daddr,
    });
}

export const platformUrlForMap = (address) => {
    const scheme = Platform.select({ ios: 'maps:0,0?q=', android: 'geo:0,0?q=' });
    const latLng = address;
    const label = address;
    return Platform.select({
        ios: `${scheme}${label}@${latLng}`,
        android: `${scheme}${latLng}(${label})`
    });
}

export const removeSquareBrackets = (str) => {
    return JSON.stringify(str).replace(/[\[\]']+/g, '');
}

//get user current
export const getCurrentLocation = () => {
    let options = {
        enableHighAccuracy: false,
        timeout: 20000,
        maximumAge: 1000
    };
    if (Platform.OS === 'android') {
        options = { enableHighAccuracy: true, timeout: 20000 }
    }
    navigator.geolocation.getCurrentPosition((position) => {
        const { latitude, longitude } = position.coords;
        return latitude + ',' + longitude;
    }, (error) => {

    }, options);
}