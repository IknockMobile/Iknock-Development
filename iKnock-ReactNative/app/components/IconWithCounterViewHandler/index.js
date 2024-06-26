import React, { Component } from 'react';
import { View, Image, Text } from "react-native";
import Images from "../../assets";
import { connect } from 'react-redux';


class IconWithCounterViewHandler extends Component {

    render() {
        return (
            <View>
                <View style={{ justifyContent: "center", alignItems: "center", marginRight: 20 }}>

                    <Image source={Images.ic_filter} style={{ height: 20, width: 20, }} />
                    {
                        this.props.count !== 0 ?
                            <View style={{
                                backgroundColor: 'red', borderRadius: 10,
                                height: 20, width: 20, alignItems: "center",
                                position: "absolute", top: -12, left: 12
                            }}>
                                <Text style={{ color: 'white' }}>{this.props.count}</Text>
                            </View>
                            :
                            <View />
                    }

                </View>

            </View >
        );
    }
}

const mapStateToProp = (state) => {
    return {
        count: state.cart.count
    }
}
export default connect(mapStateToProp)(IconWithCounterViewHandler);

// export default IconWithCounterViewHandler