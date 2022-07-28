/**
 * walks a DOM node down
 * @param vnode
 * @param cb
 */
export function walk(vnode, cb) {
  if (!vnode) return;

  if (vnode.component) {
    const proxy = vnode.component.proxy;
    if (proxy) cb(vnode.component.proxy);
    walk(vnode.component.subTree, cb);
  } else if (vnode.shapeFlag & 16) {
    const vnodes = vnode.children;
    for (let i = 0; i < vnodes.length; i++) {
      walk(vnodes[i], cb);
    }
  }
}
