import React, { Component } from 'react';
import { StyleSheet, View } from 'react-native';
import MultiSelect from 'react-native-multiple-select';
import colors from "../assets/colors";

class MultiSelectValues extends Component {
    render() {
        return (
            <View style={{ flex: 1 }}>
                <MultiSelect
                    hideTags
                    items={this.props.listItems}
                    uniqueKey="id"
                    //ref={(component) => { this.multiSelect = component }}
                    onSelectedItemsChange={this.props.onSelectedItemsChange}
                    selectedItems={this.props.selectedItems}
                    selectText="Pick Items"
                    searchInputPlaceholderText={this.props.InputPlaceholder}
                    onChangeInput={(text) => console.log(text)}
                    // altFontFamily="ProximaNova-Light"
                    tagRemoveIconColor="#CCC"
                    tagBorderColor="#CCC"
                    autoFocusInput={false}
                    tagTextColor="#CCC"
                    selectedItemTextColor={colors.DarkBlueTextColor}
                    selectedItemIconColor={colors.DarkBlueTextColor}
                    itemTextColor="#000"
                    displayKey={this.props.type === true ? "title" : "name"}
                    searchInputStyle={{ color: '#CCC' }}
                    submitButtonColor={colors.DarkBlueTextColor}
                    submitButtonText="Submit"
                />
            </View>
        );
    }
}


const styles = StyleSheet.create({
    inputText: {
        height: 50

    }
})
export { MultiSelectValues };