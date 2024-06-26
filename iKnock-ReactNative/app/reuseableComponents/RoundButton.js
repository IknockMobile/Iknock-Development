import React from 'react';
import { StyleSheet } from 'react-native';
import { Button, Text, ListItem } from 'native-base';
import colors from '../assets/colors'

const RoundButton = (props) => {
    const {btnStyle}=props;
    return (
        <ListItem noBorder onPress={props.onPress} style={btnStyle}>
            <Button rounded style={styles.contentCenter} onPress={props.onPress}>
                <Text style={{ color: colors.btnBlackTextColor }} uppercase={false}> {props.title} </Text>
            </Button>
        </ListItem>
    );
}
const styles = StyleSheet.create({
    contentCenter: {
        justifyContent: 'center',
        width: '100%',
        backgroundColor: colors.btnYellowBgColor
    }
});

export { RoundButton };