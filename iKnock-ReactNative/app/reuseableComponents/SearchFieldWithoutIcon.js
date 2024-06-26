import React from 'react';
import { Item, Icon, Input } from 'native-base';
const SearchFieldWithoutIcon = (props) => {

    return (
        <Item regular style={{ marginLeft: 15, marginRight: 15, marginTop: 15 }}>
            <Icon active name='search' />
            <Input
                value={props.value}
                placeholder='Search'
                onChangeText={props.onChangeText} />
        </Item>

    );
}

export { SearchFieldWithoutIcon };