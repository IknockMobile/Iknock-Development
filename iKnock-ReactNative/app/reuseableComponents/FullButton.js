import React from 'react';
import { StyleSheet } from 'react-native';
import { Text, ListItem, Left, Right, Icon, Button } from 'native-base';
import colors from '../assets/colors'

const FullButton = (props) => {
    return (
        <Button full style={[styles.btnStyle, props.buttonStyle]} onPress={props.onPress}>
            <Text style={{ color: colors.btnWhiteTextColor }} uppercase={false}> {props.title} </Text>
        </Button>
    );
}
const styles = StyleSheet.create({
    btnStyle: {
        height: 50,
        backgroundColor: colors.btnDarkBlueBgColor,

    }
});
export { FullButton };