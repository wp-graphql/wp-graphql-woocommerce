import styled from 'styled-components';

const Grid = styled.div.attrs((props) => ({
  columns: props.columns || 'auto-fit',
  inline: props.inline || false,
  rows: props.rows || '1fr',
  itemWidth: props.itemWidth || '375px',
  min: typeof props.columns === 'number' ? `${100 / props.columns}%` : props.itemWidth,
}))`
  padding: 12px 16px;
  width: ${({ width }) => (width || '100%')};
  height: ${({ height }) => (height || 'auto')};
  display: ${({ inline }) => (inline ? 'inline-grid' : 'grid')};
  grid-template-columns: repeat(${(props) => props.columns}, minmax(${(props) => props.min}, 1fr));
  grid-template-rows: ${(props) => props.rows};
  ${({ columnGap }) => columnGap && `grid-column-gap: ${columnGap};`}
  ${({ rowGap }) => rowGap && `grid-row-gap: ${rowGap};`}
  ${({ justifyItems }) => justifyItems && `justify-items: ${justifyItems};`}
  ${({ justifyContent }) => justifyContent && `justify-content: ${justifyContent};`}
  ${({ alignItems }) => alignItems && `align-items: ${alignItems};`}
  ${({ alignContent }) => alignContent && `align-content: ${alignContent};`}
  ${({ autoFlow }) => autoFlow && `grid-auto-flow: ${autoFlow};`}
  ${({ maxWidth }) => maxWidth && `max-width: ${maxWidth};`}
`;

export default Grid;
