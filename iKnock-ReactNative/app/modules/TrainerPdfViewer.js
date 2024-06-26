import React, { Component } from 'react'
import { StyleSheet, Dimensions, View, ActivityIndicator } from 'react-native'
import { StyleProvider, Container } from 'native-base'
import getTheme from '../theme/components';
import material from '../theme/variables/material';
import colors from '../assets/colors'
import Pdf from 'react-native-pdf';

class TrainerPdfViewer extends Component {
    render() {
        let pdf = this.props.navigation.getParam('pdf');
        const source = { uri: pdf, cache: true };
        return (
            <View style={styles.container}>
                <Pdf
                    source={source}
                    onLoadComplete={(numberOfPages, filePath) => {
                        console.log(`number of pages: ${numberOfPages}`);
                    }}
                    onPageChanged={(page, numberOfPages) => {
                        console.log(`current page: ${page}`);
                    }}
                    onError={(error) => {
                        console.log(error);
                    }}
                    style={styles.pdf} />
            </View>
        )
    }
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        justifyContent: 'flex-start',
        alignItems: 'center',
        marginTop: 0,
    },
    pdf: {
        flex: 1,
        width: Dimensions.get('window').width,
    }
});
export default TrainerPdfViewer