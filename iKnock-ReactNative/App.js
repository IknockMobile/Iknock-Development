import React from 'react';
import {Platform, SafeAreaView, View} from 'react-native';
import Setup from './app/boot/setup';
import {Provider} from 'react-redux';
import {createStore, applyMiddleware} from 'redux';
import ReduxThunk from 'redux-thunk';
import appReducer from './app/reducers';
import HttpServiceManager from './app/HttpServiceManager/HttpServiceManager';
import constant from './app/HttpServiceManager/constant';
import KeyboardManager from 'react-native-keyboard-manager';
import SplashScreen from 'react-native-splash-screen';
import colors from './app/assets/colors';
console.disableYellowBox = true;

export default class App extends React.Component {
  componentDidMount() {
    if (Platform === 'ios') {
      KeyboardManager.setToolbarPreviousNextButtonEnable(true);
    }

    SplashScreen.hide();

    HttpServiceManager.initialize(constant.baseUrl, {
      token: 'api.Pd*!(5675',
      'Content-Type': 'application/json',
    });
  }
  render() {
    return (
      <SafeAreaView style={{flex: 1}}>
        <Provider
          store={createStore(appReducer, {}, applyMiddleware(ReduxThunk))}>
          <Setup />
        </Provider>
      </SafeAreaView>
    );
  }
}
