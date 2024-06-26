import React from 'react';
import { Image, TouchableOpacity } from 'react-native';
import { Left, Right, Card, CardItem, Text } from 'native-base';
import Images from '../assets';
import styles from '../assets/styles';
import { getCurrDate } from "../Utility";
import DateTimePicker from 'react-native-modal-datetime-picker';

const DataPickerButton = (props) => {

    let date = getCurrDate();
    return (
        <TouchableOpacity onPress={props.onPress}>
            <Card noBorder >
                <CardItem style={{ backgroundColor: props.backgroundColor, height: 50, }}>
                    <Left>
                        <Text style={{ color: props.textColor }}>{props.selectedDate}</Text>
                        <DateTimePicker
                            mode='datetime'
                            isVisible={props.isVisible}
                            onConfirm={props.onConfirm}
                            onCancel={props.onCancel}
                            // minimumDate={new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes())}
                            is24Hour={false}
                        />
                        {/* <DatePicker
                        //defaultDate={new Date(2018, 4, 4)}
                        //minimumDate={new Date(2018, 1, 1)}
                        //maximumDate={new Date(2018, 12, 31)

                        locale={"en"}
                        timeZoneOffsetInMinutes={undefined}
                        modalTransparent={false}
                        animationType={"fade"}
                        androidMode={"default"}
                        placeHolderText={props.placeHolderText}
                        textStyle={{ color: props.textColor }}
                        placeHolderTextStyle={{ color: props.textColor }}
                        onDateChange={props.onDateChange}
                    /> */}
                    </Left>
                    <Right>
                        <Image source={Images.forword_arrow_blue} style={[styles.iconSize, { tintColor: props.iconColor }]}></Image>
                    </Right>
                </CardItem>
            </Card>
        </TouchableOpacity>
    );
}
// const styles = StyleSheet.create({
//     btnStyle: {
//         height: 50,
//         backgroundColor: colors.btnDarkBlueBgColor,

//     }
// });

export { DataPickerButton };