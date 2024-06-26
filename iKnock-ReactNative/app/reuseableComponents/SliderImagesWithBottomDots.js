import React, { Component } from "react"
import { Dimensions, ImageBackground } from "react-native";
import { View } from "native-base";
import Carousel, { Pagination } from 'react-native-snap-carousel';
const window = Dimensions.get('window');
const bannerWidthToSkip = 0
const SliderImagesWithBottomDots = (props) => {
    const entries = props.entries;
    return (

        <View style={{ justifyContent: "flex-end", alignItems: "center" }}>
            <Carousel
                data={entries}
                autoplay={true}
                enableMomentum={false}
                lockScrollWhileSnapping={true}
                renderItem={renterItem}
                sliderWidth={window.width}
                itemWidth={(window.width - bannerWidthToSkip)}
                onSnapToItem={props.onSelectedItem}
            />
            <Pagination
                dotsLength={entries.length}
                activeDotIndex={props.activeSlide}
                containerStyle={{
                    position: "absolute", paddingTop: 0, marginBottom: 5,
                    paddingBottom: 0
                }}
                dotContainerStyle={{ marginLeft: 0 }}
                dotStyle={{
                    width: 8,
                    height: 8,
                    borderRadius: 5,
                    marginLeft: 0,
                    marginBottom: 10,
                    padding: 0,
                    backgroundColor: 'red'
                }}
                inactiveDotStyle={{
                    // Define styles for inactive dots here
                }}
                inactiveDotOpacity={0.46}
                inactiveDotScale={0.95} />
        </View>
    )
}
export { SliderImagesWithBottomDots }

const renterItem = ({ item, index }) => {
    return <View style={{ height: 230 }}>
        {typeof (item.path) === "string" && item.path.startsWith("http") ?
            <ImageBackground source={{ uri: item.path }} style={{ height: 230, width: window.width }} />
            :
            <ImageBackground source={{ uri: 'file://' + item.path }} style={{ height: 230, width: window.width }} />
        }

    </View>
};
