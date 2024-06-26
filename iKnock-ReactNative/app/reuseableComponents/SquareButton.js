import React from 'react';
import { StyleSheet } from 'react-native';
import { Button, Text, ListItem, Item, Icon, Right } from 'native-base';
import colors from '../assets/colors'

const SquareButton = (props) => {
    return (
        <Button block style={[styles.btnStyle, props.buttonStyle]} onPress={props.onPress} iconRight>
            <Text style={{ color: colors.White }} uppercase={false}> {props.title} </Text>

            {/* <Icon name='arrow-forward' /> */}

        </Button>
    );
}
const styles = StyleSheet.create({
    btnStyle: {
        flex: 1,
        margin: 5,
        minHeight: 50,
        backgroundColor: colors.btnDarkBlueBgColor
    }
});

export { SquareButton };