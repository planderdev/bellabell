import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import {execFileSync} from 'node:child_process';

execFileSync(process.execPath,['tools/build-design-system.mjs','--check'],{stdio:'inherit'});
const upstream=JSON.parse(fs.readFileSync('design-system/base-ui/tokens.json','utf8'));
const css=fs.readFileSync('assets/css/tokens.css','utf8');
const declarations=[...css.matchAll(/--([\w-]+):\s*([^;]+);/g)];
const actual=Object.fromEntries(declarations.map(m=>[m[1],m[2]]));
assert.equal(declarations.length,Object.keys(actual).length,'Each token must be declared exactly once');
assert.equal((css.match(/:root\s*\{/g)||[]).length,1);
const branding=new Set(['brand','brand-on','brand-ink','brand-hover','brand-pressed','brand-tint','brand-tint-dark','brand-inverse','brand-progress']);
let count=2;
for(const [key,value] of Object.entries({...upstream.global,...upstream.light})){
  if(branding.has(key))continue;
  assert.equal(actual[key],String(value),`BASE UI token changed: ${key}`);count++;
}
assert.equal(actual.brand,'#00AF84');count++;
const context={UI:{}};
vm.runInNewContext(fs.readFileSync('design-system/base-ui/brand-source.js','utf8'),context);
// Requested white foreground is a deliberate brand choice, not an AA contrast claim.
assert.equal(actual['brand-on'],'#FFFFFF');
assert.ok(context.UI.color.contrast(actual['brand-ink'],'#FFFFFF')>=4.5);count+=2;
for(const legacy of ['components','responsive','animations','home-motion']){
  assert.equal(fs.existsSync(`assets/css/${legacy}.css`),false,`Legacy layer still exists: ${legacy}`);count++;
}
for(const file of ['base','layout','home','details']){
  const source=fs.readFileSync(`assets/css/${file}.css`,'utf8');
  assert.doesNotMatch(source,/--color-|--gutter|--on-dark|--secondary|--radius-control/);
  assert.doesNotMatch(source,/#[\da-f]{3,8}\b|rgba?\(/i,'Page styles must use BASE UI colors');count+=2;
}
console.log(`${count} design-system token, contrast and replacement assertions passed.`);
console.log(`Requested brand/white contrast: ${context.UI.color.contrast(actual.brand,actual['brand-on']).toFixed(2)}:1 (below 4.5:1).`);
