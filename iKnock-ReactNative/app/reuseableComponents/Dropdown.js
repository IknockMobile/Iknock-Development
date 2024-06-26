import React from 'react';
import { StyleSheet, Platform } from 'react-native';
import { Item, Picker, Icon } from 'native-base';

const Dropdowns = (props) => {
    let serviceItems;
    if (props.label === 'All Status' || props.label === 'All Type') {
        serviceItems = props.listItems.map((s, i) => {
            return <Picker.Item key={s.id} value={s.id} label={s.title} />
        });
    } else {
        serviceItems = props.listItems.map((s, i) => {
            return <Picker.Item key={s.id} value={s.id} label={s.name} />
        });
    }
    return (
        <Item picker style={styles.inputText} >
            <Picker
                mode="dropdown"
                iosIcon={<Icon name="arrow-down" />}
                style={{ width: (Platform.OS === 'ios') ? props.width : '100%' }}
                placeholder={props.placeholder}
                placeholderStyle={{ color: "#bfc6ea" }}
                placeholderIconColor="#007aff"
                headerBackButtonText="Back"
                headerBackButtonTextStyle={{ color: "#fff" }}
                headerTitleStyle={{ color: "#fff" }}
                itemTextStyle={{ textAlign: 'center' }}
                selectedValue={props.selectedValue}
                onValueChange={props.onValueChange}>
                <Picker.Item label={props.label} value={""} />
                {serviceItems}
            </Picker>
        </Item >
    );
}

const styles = StyleSheet.create({
    inputText: {
        height: 50

    }
})
export { Dropdowns };