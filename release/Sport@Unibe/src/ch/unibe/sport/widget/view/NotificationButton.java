package ch.unibe.sport.widget.view;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.util.AttributeSet;
import android.util.TypedValue;
import android.widget.ImageView;

public class NotificationButton extends ImageView {
	
	private final Paint circlePaint = new Paint(Paint.ANTI_ALIAS_FLAG);
	private final Paint notifPaint = new Paint(Paint.ANTI_ALIAS_FLAG|Paint.SUBPIXEL_TEXT_FLAG);
	
	private int notifications;
	private String notifText;
	
	private int circleColor;
	private int notifColor = Color.WHITE;
	
	private float circlePaddingTop;
	
	public NotificationButton(Context context) {
		super(context);
		init();
	}
	
	public NotificationButton(Context context, AttributeSet attrs) {
		super(context, attrs);
		init();
	}
	
	public NotificationButton(Context context, AttributeSet attrs, int defStyle) {
		super(context, attrs, defStyle);
		init();
	}
	
	private void init(){
		initNotification(6);
		initCircleColor(0xFFd4080e);
		initCirclePaddingTop(3.0f);
		initNotifColor(Color.WHITE);
		notifPaint.setFakeBoldText(true);
	}
		
	private void initCirclePaddingTop(float padding){
		this.circlePaddingTop = dpToPx(padding);
	}
	
	private void initCircleColor(int color){
		circleColor = color;
		this.circlePaint.setColor(circleColor);
	}
	
	private void initNotifColor(int color){
		this.notifColor = color;
		notifPaint.setColor(notifColor);
	}
	
	private void initNotification(int notif){
		this.notifications = notif;
		this.notifText = ""+notif;
		if (this.notifications > 0){
			this.setScaleType(ImageView.ScaleType.FIT_START);
		}
		else {
			this.setScaleType(ImageView.ScaleType.CENTER);
		}
	}

	public void setNotifications(int notif){
		initNotification(notif);
		this.invalidate();
	}
	
	@Override
	protected void onDraw(Canvas canvas){
		super.onDraw(canvas);
		if (this.notifications > 0){
			
			float viewHeight = getHeight();
			float viewWidth = getWidth();

			float rectWidth = Math.min(viewWidth/2,viewHeight/2);
			float radius = rectWidth/2;

			float textSize = radius*4/3;
			this.notifPaint.setTextSize(textSize);
			
			float textWidth = notifPaint.measureText(notifText);
			
			float maxTextWidth = radius/2*(float)Math.sqrt(3d);
			
			/* should make text smaller to fit circle */
			if (textWidth > maxTextWidth){
				float koeff = (textWidth/maxTextWidth);	// how smaller textSize should be
				koeff = koeff / Math.min(1.5f, koeff);
				textSize = textSize/koeff;
				this.notifPaint.setTextSize(textSize);
				textWidth = notifPaint.measureText(notifText);
			}
			
			float circleX = viewWidth-radius;
			float circleY = this.circlePaddingTop+radius;
			
			canvas.drawCircle(circleX, circleY, radius, circlePaint);
			
			float textX = circleX-textWidth/2;
			float textY = circleY-(notifPaint.descent() + notifPaint.ascent())/ 2;
			
			canvas.drawText(notifText, textX, textY, notifPaint);
		}
	}

	private float dpToPx(float dp){
		return TypedValue.applyDimension(TypedValue.COMPLEX_UNIT_DIP, dp, getContext().getResources().getDisplayMetrics());
	}

}
