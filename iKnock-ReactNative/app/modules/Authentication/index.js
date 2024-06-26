import React, {Component} from 'react';
import {Image} from 'react-native';
import {Container, View} from 'native-base';
import Images from '../../assets';
import {getUserData, clearAll} from '../../UserPreference';
import {StackActions, NavigationActions} from 'react-navigation';
import {SpinnerView} from '../../Utility/common';
import colors from '../../assets/colors';
import constant from '../../HttpServiceManager/constant';
import httpServiceManager from '../../HttpServiceManager/HttpServiceManager';
import {push} from '../../services/NavigationService';

class Authentication extends Component {
  constructor(props) {
    super(props);
    this.state = {
      loadng: true,
    };
    this.checkIsLogin();
  }

  checkIsLogin = () => {
    getUserData().then(response => {
      if (response !== undefined && response !== null) {
        this._navigateTo('primaryStack');
        // this._checkActiveUser();
      } else {
        this._navigateTo('loginStack');
      }
    });
  };

  _checkActiveUser = () => {
    httpServiceManager
      .getInstance()
      .request(constant.getLeadList + '?is_web=0', '', 'get')
      .then(response => {
        this._navigateTo('primaryStack');
      })
      .catch(error => {
        if (error.indexOf('token is invalid')) {
          clearAll();
          this._navigateTo('loginStack');
        }
      });
  };

  _navigateTo = rounts => {
    this.setState({
      loadng: false,
    });

    push(rounts);

    // const resetAction = StackActions.reset({
    //   index: 0,
    //   actions: [
    //     NavigationActions.navigate({ routeName: rounts }),
    //   ],
    // });
    // this.props.navigation.dispatch(resetAction);
  };
  render() {
    return (
      <Container style={{backgroundColor: colors.DarkBlue}}>
        <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
          <Image
            source={Images.logo}
            style={{
              width: 200,
              height: 100,
            }}
          />
        </View>
        {this.state.loadng && <SpinnerView />}
      </Container>
    );
  }
}
export default Authentication;
