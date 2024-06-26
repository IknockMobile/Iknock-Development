import React, { Component } from 'react';
import { View, Image, ImageBackground } from 'react-native';
import {
  Container,
  Content,
  Form,
  Text,
  ListItem,
  Left,
  Body, Button
} from 'native-base';
import styles from '../../assets/styles';
import Images from '../../assets';
import { InputField, RoundButton } from '../../reuseableComponents';
import colors from '../../assets/colors';
import { emailChanged, forgotPassword } from '../../actions';
import { connect } from 'react-redux';
import { SpinnerView } from '../../Utility/common';
import { Toasts } from "../../Utility/showToast";
import { isEmailValid } from "../../Utility/validations";
import { VALIDEMAIL } from '../../constants/Constants';

class ForgotPassword extends Component {

  constructor(props) {
    super(props)
    this.state = ({
      email: ''
    });
  }
  onPressSubmitButton = () => {
    const { email } = this.state
    if (!isEmailValid(email)) {
      Toasts.showToast(VALIDEMAIL)
    } else {
      this.props.forgotPassword({ email }, true, this.cbSuccess, this.cbFailure);
    }
  }

  cbSuccess = (response) => {
    Toasts.showToast(response.message, 'success');
    setTimeout(() => {
      this.props.navigation.goBack();
    }, 800);

  }
  cbFailure = (error) => {
    Toasts.showToast(error)
  }
  // componentDidUpdate() {
  //   if (this.props.error !== '') {
  //     Toasts.showToast(this.props.error)
  //   } else if (this.props.message !== '') {
  //     Toasts.showToast(this.props.message);
  //     this.props.navigation.goBack();
  //   }
  // }

  render() {
    const { email } = this.state;
    return (
      <Container>
        <ImageBackground style={{ flex: 1 }} source={Images.bg}>
          <Content contentContainerStyle={{marginTop:130, padding: 24 }}>
            <View style={{ alignItems: 'center' }}>
              <Image style={[styles.logoSize]} source={Images.logo}
                resizeMode={'contain'} />
              <Text style={{ color: colors.YellowTextColor }} uppercase={false}> Forgot Password </Text>
            </View>
            <Form>
              <InputField
                onChangeText={(text) => this.setState({ email: text })}
                label={"Email"}
                value={email}
                secureTextEntry={false}
                placeholder={"Enter Email Address"}
              />
            </Form>
            <RoundButton
              onPress={() => this.onPressSubmitButton()}
              title={"Submit"}
              btnStyle={{ marginTop: 16 }} />

          </Content>
        </ImageBackground>
        {this.props.loading && <SpinnerView />}
      </Container>

    );
  }
}

const mapStateToProp = state => {
  return {
    email: state.auth.email,
    error: state.auth.error,
    loading: state.auth.loading,
    message: state.auth.message,
  }
}
export default connect(mapStateToProp,
  {
    emailChanged,
    forgotPassword
  }
)(ForgotPassword)