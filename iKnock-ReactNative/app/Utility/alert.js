import React from 'react';
import { Alert } from "react-native";
export const alert = (message) => {
    // Works on both iOS and Android
    Alert.alert(
        message,
        [
            {
                text: 'Cancel',
                onPress: () => console.log('Cancel Pressed'),
                style: 'cancel',
            },
            { text: 'OK', onPress: () => console.log('OK Pressed') },
        ],
        { cancelable: false },
    );
}
