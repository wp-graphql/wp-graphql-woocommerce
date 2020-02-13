import styled from 'styled-components';

const Rail = styled.div`
  display: ${({ inline }) => (inline ? 'inline-flex' : 'flex')};
  flex-direction: ${({ direction }) => direction || 'row'};
  width: ${({ width }) => width || 'auto'};
  height: ${({ height }) => height || 'auto'};
  justify-content: ${({ justifyContent }) => justifyContent || 'start'};
  justify-items: ${({ justifyItems }) => justifyItems || 'auto'};
  align-content: ${({ alignContent }) => alignContent || 'stretch'};
  align-items: ${({ alignItems }) => alignItems || 'center'};
  align-self: ${({ alignSelf }) => alignSelf || 'auto'};
  flex-grow: ${({ grow }) => {
    if (grow) {
      return (typeof grow === 'number') ? grow : '1';
    }
    return '0';
  }};
  flex-shrink: ${({ shrink }) => {
    if (shrink) {
      return (typeof shrink === 'number') ? shrink : '1';
    }
    return '0';
  }};
  flex-basis: ${({ basis }) => basis || 'auto'};
  ${({ scrollXOverflow }) => scrollXOverflow && 'overflow-x: scroll;'}
  ${({ margin }) => margin && `margin: ${margin};`}
  ${({ padding }) => padding && `padding: ${padding};`}

  &::-webkit-scrollbar-track {
    border-radius: 5px;
  }

  &::-webkit-scrollbar {
    height: 6px;
    background-color: #F5F5F5;
  }

  &::-webkit-scrollbar-thumb {
    border-radius: 5px;
    box-shadow: inset 0 0 3px rgba(0,0,0,.3);
    background-color: #F90;
    background-image: -webkit-linear-gradient(
      45deg,
      rgba(255, 255, 255, .2) 25%,
      transparent 25%,
      transparent 50%,
      rgba(255, 255, 255, .2) 50%,
      rgba(255, 255, 255, .2) 75%,
      transparent 75%,
      transparent
    );
  }

  ${({ scrollPadding }) => scrollPadding && `
    &:last-child:after {
      content: "";
      display: block;
      width: ${scrollPadding};
      height: 20px;
    }
  `}
`;
export const ProductRail = styled(Rail)`
  .product-name,
  .product-price {
    text-decoration: none;
    margin: 0.5em 0.5em 0;
  }

  .product-name {
    color: #252A31;
    font-size: 16px;
    text-align: center;
    transition: color 450ms ease;
    small {
      color: #5F738C;
    }

    .product-price {
      color: #252A31;
      font-size: 14px;
      .regular-price {
        text-decoration: line-through;
        color: #5F738C;
      }
    }

    &:hover {
      color: #00A991;

      .product-price {
        color: #181B20;
        .regular-price {
          text-decoration: line-through;
          color: #52647A;
        }
      }
    }
  }
`;

export default Rail;