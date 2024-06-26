import React, { Component } from 'react'
import { StyleSheet, ActivityIndicator } from 'react-native'
import { StyleProvider, Container } from 'native-base';
import { WebView } from 'react-native-webview';

class WebViews extends Component {
    ActivityIndicatorLoadingView() {
        //making a view to show to while loading the webpage
        return (
            <ActivityIndicator
                color={'black'}
                size="large"
                style={styles.ActivityIndicatorStyle}
            />
        );
    }
    render() {
        const navigation = this.props.navigation
        const url = navigation.getParam('url');
        return (
            <Container>
                <WebView
                    style={styles.WebViewStyle}
                    source={{ uri: url }}
                    //Enable Javascript support
                    javaScriptEnabled={true}
                    //For the Cache
                    domStorageEnabled={true}
                    //View to show while loading the webpage
                    renderLoading={this.ActivityIndicatorLoadingView}
                    //Want to show the view or not
                    startInLoadingState={true}
                />


            </Container>
        )
    }
}
const styles = StyleSheet.create({
    WebViewStyle: {
        justifyContent: 'center',
        alignItems: 'center',
        flex: 1
    },

    ActivityIndicatorStyle: {
        flex: 1,
        justifyContent: 'center',
    },
});
export default WebViews