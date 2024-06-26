import React, {Component} from 'react';
import {View, Image, ImageBackground, Linking} from 'react-native';
import {Container, Content, Form, Text, Button} from 'native-base';
import {connect} from 'react-redux';
import styles from '../../assets/styles';
import Images from '../../assets';
import {InputField, RoundButton} from '../../reuseableComponents';
import colors from '../../assets/colors';
import {emailChanged, passwordChanged, loginUser} from '../../actions';
import {isEmailValid, isValidPassword} from '../../Utility/validations';
import {Toasts} from '../../Utility/showToast';
import {VALIDEMAIL, VALIDPASSWORD} from '../../constants/Constants';
import {getUserData} from '../../UserPreference';
import {SpinnerView} from '../../Utility/common/SpinnerView';
import {push} from '../../services/NavigationService';

class Login extends Component {
  state = {
    isHide: true,
    email: __DEV__ ? 'smit.laravel@gmail.com' : '', //Paige@letsgetpaid.com  // peter@yopmail.com renee@semaphoremobile.com  michael@letsgetpaid.com
    password: __DEV__ ? 'smit.laravel@gmail.com' : '', //Letsgetpaid2020  peter@yopmail.com   Test1234*
    // email: __DEV__ ? 'Paige@letsgetpaid.com' : '', //Paige@letsgetpaid.com  // peter@yopmail.com renee@semaphoremobile.com  michael@letsgetpaid.com
    // password: __DEV__ ? '@Buda2015' : '', //Letsgetpaid2020  peter@yopmail.com   Test1234*
  };

  onPressLoginButton = () => {
    const {email, password} = this.state;
    if (!isEmailValid(email)) {
      Toasts.showToast(VALIDEMAIL);
    } else if (!isValidPassword(password)) {
      Toasts.showToast(VALIDPASSWORD);
    } else {
      this.props.loginUser(
        {email, password},
        true,
        this.cbSuccess,
        this.cbFailure,
      );
    }
  };
  cbSuccess = (response) => {
    this._navigateTo('primaryStack');
  };
  cbFailure = (error) => {
    Toasts.showToast(error);
  };
  onPressForgotPasswordButton = () => {
    this.props.navigation.navigate('forgotPassword');
  };

  onPressPrivacy = () => {
    Linking.openURL('https://iknockapp.com/privacypolicy');
  };

  _navigateTo = (routs) => {
    push(routs);
  };
  render() {
    const {email, password, isHide} = this.state;
    return (
      <Container>
        <ImageBackground style={{flex: 1}} source={Images.bg}>
          <Content
            contentContainerStyle={{
              flex: 1,
              justifyContent: 'center',
              padding: 24,
            }}>
            <View style={{alignItems: 'center'}}>
              <Image
                style={[styles.logoSize]}
                source={Images.logo}
                resizeMode={'contain'}
              />
            </View>
            <Form>
              <InputField
                onChangeText={(text) => this.setState({email: text})}
                label={'Email'}
                secureTextEntry={false}
                value={email}
                keyboardType="email-address"
                placeholder={'Enter Email Address'}
              />
              <InputField
                onChangeText={(text) => this.setState({password: text})}
                label={'Password'}
                secureTextEntry={isHide}
                value={password}
                placeholder={'Enter Password'}
                iconImg={
                  isHide
                    ? Images.ic_onboarding_visible
                    : Images.ic_oboarding_hide
                }
                iconPressed={() => this.setState({isHide: !isHide})}
              />
            </Form>
            <RoundButton
              onPress={() => this.onPressLoginButton()}
              title={'Login'}
              btnStyle={{marginTop: 16}}
            />
            <Button
              full
              transparent
              onPress={() => this.onPressForgotPasswordButton()}>
              <Text style={{color: colors.White}} uppercase={false}>
                {' '}
                Forgot Password?{' '}
              </Text>
            </Button>

            <Button full transparent onPress={() => this.onPressPrivacy()}>
              <Text style={{color: colors.White}} uppercase={false}>
                Privacy Policy
              </Text>
            </Button>
          </Content>
          <View style={{justifyContent: 'flex-end'}}>
            <Image
              source={require('../../assets/Images/bottom-logo.png')}
              style={{
                width: 100,
                height: 80,
                alignSelf: 'flex-end',
                marginRight: 20,
                marginBottom: 20,
              }}
              resizeMode={'stretch'}
            />
          </View>
        </ImageBackground>
        {this.props.loading && <SpinnerView />}
      </Container>
    );
  }
}

const mapStateToProp = (state) => {
  return {
    email: state.auth.email,
    password: state.auth.password,
    error: state.auth.error,
    loading: state.auth.loading,
    user: state.auth.user,
  };
};
export default connect(mapStateToProp, {
  emailChanged,
  passwordChanged,
  loginUser,
})(Login);
